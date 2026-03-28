#include <stdio.h>

int main() {
    int dias;
    float bruto, liquido, gratificacao = 0;
    const float diaria = 50.25;

    puts("Digite o número de dias trabalhados: ");
    scanf("%d", &dias);

    bruto = dias * diaria;

    if (dias > 20) {
        gratificacao = bruto * 0.30;
    } else if (dias > 10) {
        gratificacao = bruto * 0.20;
    }

    bruto += gratificacao;
    liquido = bruto * 0.90;

    printf("Valor líquido a ser pago: R$ %.2f\n", liquido);
    return 0;
}