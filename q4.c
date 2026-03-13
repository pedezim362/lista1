#include <stdio.h>

int main() {

    double v;
    puts("Qual foi o valor da sua conta? ");
    scanf("%lf", &v);

    printf("O valor da sua conta, com os 10%%, é: %.2f.\n", v * 1.1);

    return 0;
}