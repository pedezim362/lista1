#include <stdio.h>

int main() {

    float vr, cot;
    puts("Quantos reais você deseja converter para dólares?");
    scanf("%f", &vr);
    puts("Qual é a cotação atual do dólar?");
    scanf("%f", &cot);

    printf("Com R$%.2f, você pode comprar $%.2f.\n", vr, vr*cot);

    return 0;
}